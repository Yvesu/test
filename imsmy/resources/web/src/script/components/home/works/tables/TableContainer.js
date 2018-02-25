import React, { Component } from 'react'
import { Table, Pagination ,Icon, Spin, Button } from 'antd'
import Fetch from 'utils/fetch.js'

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
     }
  }

  onSelectChange(selectedRowKeys,selectedRows){
    this.setState({ selectedRowKeys})
    //获取批量操作的表格ID
    if(this.props.onChange){
      this.props.onChange(selectedRowKeys)
    }
    //将rowKey改为record.id就可以选中它的id了
  }


  handleChangeFetch=()=>{
    this.setState({loading:true})
    Fetch.post({
        uri:this.props.uri,
        callback:(res)=>{
          console.log(res,'表格的数据');
          if(res.data){
            this.setState({
              loading:false,
              data:res.data
            })
          }else if(res.tweets){
            this.setState({
              loading:false,
              data:res.tweets
            })
          }
          if(this.props.RefreshTableState===true){
            this.props.RefreshTableState = false
          }
        },
        formData:this.props.formData
      })
  }
  handleChangeFetchFormaData=()=>{
      this.setState({loading:true})
      let formData = new FormData()
      //素材页面

      //数据请求
      Fetch.post({
        uri:this.props.uri,
        callback:(res)=>{
          console.log(res,'表格的数据--带参数');
          if(res.data){
            this.setState({
              loading:false,
              // stationData:res.tweets,
              data:res.data
            })
          }else if(res.tweets){
            this.setState({
              loading:false,
              // stationData:res.tweets,
              data:res.tweets
            })
          }

        },
        formData:formData
      })

    }

render() {
    const pagination ={
      showQuickJumper:true,
      showSizeChanger:true
    }
    const { selectedRowKeys, selectedRows } = this.state
    const rowSelection = {
       selectedRowKeys,
       onChange: this.onSelectChange.bind(this)
     }
    const rowKeys = selectedRowKeys.length
    return (
      <Spin spinning={this.state.loading}>
        <div className="table_container">
          <div className={(rowKeys>0) ? "tables_btn_show" : 'tables_btn_hide'}>
            <span className="tables_span">{`选中${rowKeys}个`}</span>
              { this.props.DropdownMenu }
          </div>
          <Table
            className="table_need_modify"
            rowKey={record => record.id}
            pagination={pagination}
            rowSelection={rowSelection}
            columns={this.props.columns}
            dataSource={this.state.data}  />
        </div>
      </Spin>
    );
  }
  componentDidMount(){
    // this.handleChangeFetch()
  }
  componentWillReceiveProps(nextProps){
    // console.log(nextProps,'table');

  }

}

export default TableContainer
