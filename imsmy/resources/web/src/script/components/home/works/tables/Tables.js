import React, { Component } from 'react'
import { Table, Pagination ,Icon, Spin,message } from 'antd'
import Fetch from 'utils/fetch.js'
import FilterMenu from './common/FilterMenu'
import ModalVideo from './common/ModalVideo'
//表格
class TableContainer extends Component {
  constructor(props){
    super(props)
     this.state={
       loading:false,
       // stationData:[],
       data:[],
       selectedRowKeys: [],
       selectedRows:[],
       filterPlayVisible:false,
       filterActiveVisible:false,
       sorterOrder:'',
       sorterType:'',
       playCount:'',
       filterStatus:'',
       total:0,
       pageSize:10,
       pageCurrent:1
     }
  }

  onSelectChange = (selectedRowKeys,selectedRows)=>{
    //将rowKey改为record.id就可以选中它的id了
    this.setState({ selectedRowKeys})
    //获取批量操作的表格ID
    //是否显示批量操作按钮
    if(this.props.isBatchBtnShow){
      if(selectedRowKeys.length>0){
        this.props.isBatchBtnShow(true,selectedRowKeys.length)
      }else{
        this.props.isBatchBtnShow(false,0)
      }
    }
    //获取批量操作选中的Id
    if(this.props.GetSelectId){
      this.props.GetSelectId(selectedRowKeys)
    }

  }

  //请求数据
  handleChangeFetch=()=>{
    this.setState({loading:true})
    let formData= new FormData()
    if(this.props.active){
      formData.append('active',this.props.active)
    }
    if(this.state.sorterType !==''){
      formData.append('type',this.state.sorterType)
    }
    if(this.state.sorterOrder !==''){
      formData.append('uod',this.state.sorterOrder)
    }
    if(this.state.playCount !== ''){
      formData.append('count',this.state.playCount)
    }
    if(this.state.filterStatus !== ''){
      formData.append('status',this.state.filterStatus)
    }
    if(this.state.pageCurrent !== 1){
      formData.append('page',this.state.pageCurrent)
    }
    if(this.state.pageSize !== 10){
      formData.append('everypagenum',this.state.pageSize)
    }
    Fetch.post({
        uri:this.props.uri,
        callback:(res)=>{
          // console.log(res,'表格的数据');
          if(res.data){
            this.setState({
              loading:false,
              data:res.data,
              total:res.dataNum,
            })
          }
          if(this.props.RefreshTableState===true){
            this.props.RefreshTableState = false
          }
        },
        formData:formData
      })
  }
  //表格的onchange函数
  handleOnTableChange=(pagination, filters, sorter)=>{
    // console.log('paramsLindong', pagination);
    // console.log('fileterLiag',filters);
    // console.log('sorter',sorter);
    //排序类别
    if(sorter.columnKey){
      if(sorter.columnKey==='time'){
        this.state.sorterType = 1
      }else if(sorter.columnKey==='playnum'){
        this.state.sorterType = 2
      }
    }else{
      this.state.sorterType = ''
    }
    //升序还是降序
    if(sorter.order){
      if(sorter.order==="ascend"){
        this.state.sorterOrder =2
      }else if(sorter.order==="descend"){
        this.state.sorterOrder = 1
      }
    }else{
      this.state.sorterOrder=''
    }
    //改变页数
    if(pagination.current>1){
      this.state.pageCurrent = pagination.current
    }else{
      this.state.pageCurrent = ''
    }
    //改变每页显示数
    if(pagination.pageSize>10){
      if(pagination.pageSize !==this.state.pageSize){
        this.state.pageCurrent = ''
        pagination.current=1
      }
      this.state.pageSize = pagination.pageSize
    }else{
      if(pagination.pageSize !==this.state.pageSize){
        this.state.pageCurrent = ''
        pagination.current=1
      }
      this.state.pageSize = 10
    }
    //重新执行请求函数
    this.handleChangeFetch()
    if(this.props.RunRefreshTable){
      this.props.RunRefreshTable(true)
    }
  }
  //筛选菜单的显示与否
  handleChangeFilterVisible=()=>{
    if(this.state.filterPlayVisible===true){
        this.setState({
          filterPlayVisible:false
        })
    }else if(this.state.filterActiveVisible===true){
      this.setState({
        filterActiveVisible:false
      })
    }
  }
  //根据播放量进行筛选
  handleChangePlayCountValue=(value)=>{
    this.state.playCount = value
    this.handleChangeFetch()
    if(this.props.RunRefreshTable){
      this.props.RunRefreshTable(true)
    }
  }
  //根据状态进行筛选
  handleChangeFilterStatusValue=(value)=>{
    this.state.filterStatus=value
    this.handleChangeFetch()
    if(this.props.RunRefreshTable){
      this.props.RunRefreshTable(true)
    }
  }
  //对当前项进行删除
  handleChangeDeleteRows=(valueId)=>{
    let formData = new FormData()
    formData.append('id',valueId)
    // console.log(valueId);
    Fetch.post({
      uri:this.props.deleteUri,
      callback:(res)=>{
        console.log(res);
        if(res.message && res.message==="删除成功"){
          message.success(res.message)
          if(this.props.RunRefreshTable){
            this.props.RunRefreshTable(true)
          }
        }else{
          message.error(res.error || res.message)
          if(this.props.RunRefreshTable){
            this.props.RunRefreshTable(true)
          }
        }
      },
      formData:formData
    })
  }

render() {
    const pagination ={
      showQuickJumper:true,
      showSizeChanger:true,
      total:this.state.total,
      current:this.state.pageCurrent
    }
    const { selectedRowKeys, selectedRows } = this.state
    const rowSelection = {
       selectedRowKeys,
       onChange: this.onSelectChange
     }
    const rowKeys = selectedRowKeys.length
    //表格列集合
    const columns = [
      { title: '作品', dataIndex: 'cover',key: 'cover',  width:"10.7%",
        render:(text,record) =>(
          <div className="tables_opus_box">
            {
              record.active==="处理中"?<div className="table_opus_active">
                <span>{record.active}</span>
              </div> : <ModalVideo  cover={record.cover} duration={record.duration}
                videoUrl={record.address}
              />

            }

          </div>)
      },
      { title: '标题', dataIndex: 'name', key: 'name', width:"20%",
        // render:(text,record) => ("这是一段描述，这是一段描述，这是一段描述这是一段描述这是一段描述")
      },

      { title:  "上传日期", dataIndex: "time" , key: "time",width:"18%",sorter: true,
        render:(text,record) =>(<p>{record.time!==null? record.time.date.replace('.000000','') : null}</p>)
      },
      { title: "播放量", dataIndex:  "playnum" , key: "playnum" , width:"15%",sorter: true,
        filterDropdown:(<FilterMenu uri='/api/test/getcount'
          isFilterShow={this.handleChangeFilterVisible}
          changePlayCount={this.handleChangePlayCountValue}/>),
        filterDropdownVisible:this.state.filterPlayVisible,
        onFilterDropdownVisibleChange: (visible) => {
           this.setState({
             filterPlayVisible: visible
           })
         }
      },
      { title: "状态", dataIndex: 'active', key: 'active', width:"15%",
        filterDropdown:(<FilterMenu uri='/api/test/getstatus'
          isFilterShow={this.handleChangeFilterVisible}
          changeFilterStatus={this.handleChangeFilterStatusValue} />),
        filterDropdownVisible:this.state.filterActiveVisible,
        onFilterDropdownVisibleChange: (visible) => {
          this.setState({
            filterActiveVisible: visible
          })
        }
      },
      { title: '操作', dataIndex: '操作', key: 'more',width:'10%',
        render:(text,record,index) =>(
          <div className='tables_operate'>
            <a href="javascript:void(0)">
              <Icon type="edit" />
            </a>
            <a href="javascript:void(0)" onClick={()=>this.handleChangeDeleteRows(record.id)}>
              <Icon type="delete" />
            </a>
          </div>)
      }
    ]

    return (
      <Spin spinning={this.state.loading}>
        <div className="table_container">
          <Table
            ref='twss'
            className="table_need_modify"
            rowKey={record => record.id}
            pagination={pagination}
            rowSelection={rowSelection}
            columns={columns}
            onChange={this.handleOnTableChange}
            dataSource={this.state.data}  />
        </div>
      </Spin>
    );
  }
  componentDidMount(){
    this.handleChangeFetch()
  }
  componentWillReceiveProps(nextProps){
    if(this.props.RefreshTableState !== nextProps.RefreshTableState){
      this.handleChangeFetch()
    }
  }

}

export default TableContainer
