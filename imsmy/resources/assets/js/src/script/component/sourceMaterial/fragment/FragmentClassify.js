import React, { Component } from 'react'
import { Link } from 'react-router'
import { Tabs, Badge, Button, Icon } from 'antd'
const TabPane = Tabs.TabPane

import TableContainer from '../../common/table/TableContainer'
import { sortNormalFragment, sortDisableFragment } from '../common/table/columns/MaterialColumns'
import SortNormalMenu from '../common/table/dropdownMenu/SortNormalMenu'
import SortDisableMenu from '../common/table/dropdownMenu/SortDisableMenu'
import MaodalSort from '../modal/ModalSort'
import Fetch from 'utils/fetch'
import ResolveObj from 'utils/ResolveObj'

class FragmentClassify extends Component{
  constructor(props){
    super(props)
    this.state={
      BatchNumber:[],
      countNum:0,
      countNumKey:0
    }
  }

  handleBatchMenu=(value)=>{
    // console.log(value,'hahh');
    this.setState({
      BatchNumber:value
    })
  }
  handleFormData=()=>{
    let formData = new FormData()
    formData.append('active',1)
    return formData
  }
  handleFormDataDisable=()=>{
    let formData = new FormData()
    formData.append('active',0)
    return formData
  }
  handleGetCountNum=(value)=>{
    // console.log(value);
    this.setState({
      countNum:value
    })
  }
  handleChangeTabPane=(key)=>{
    let formData = new FormData()
    if(key !== 2 ){
      formData.append('active',1)
    }else{
      formData.append('active',0)
    }
    Fetch.post({
      uri:'/api/admins/fodder/fragment/type',
      callback:(res)=>{
        // console.log(res);
        if(res.sum){
          if(key !==2){
            this.setState({
              countNum:res.sum
            })
          }else{
            this.setState({
              countNumKey:res.sum
            })
          }
        }
      },
      formData:formData
    })
  }
  render(){

    return(
      <div className="card-container card_box">
        <Tabs type="card"  tabBarExtraContent={<MaodalSort name='fragement/classify/cover/admins/'/> }
          onChange={this.handleChangeTabPane}
        >
          <TabPane tab={<p>正常
            <Badge count={this.state.countNum} overflowCount={999}
              style={{backgroundColor: '#fff', color: '#999',
               boxShadow: '0 0 0 1px #d9d9d9 inset',margin:'-3px 0 0 15px'}}
             /></p>} key="1" >

            <TableContainer columns={sortNormalFragment} uri="/api/admins/fodder/fragment/type"
              formData={this.handleFormData()}
              onChange={this.handleBatchMenu}
              DropdownMenu={<SortNormalMenu SelectAll={this.state.BatchNumber}/>}
          />
          </TabPane>
          <TabPane tab={<p>停用
            <Badge count={this.state.countNumKey}  overflowCount={999}
               style={{backgroundColor: '#fff', color: '#999',
               boxShadow: '0 0 0 1px #d9d9d9 inset',margin:'-3px 0 0 15px'}}
             /></p>} key="2" >

            <TableContainer columns={sortDisableFragment} uri="/api/admins/fodder/fragment/type"
              formData={this.handleFormDataDisable()}
              onChange={this.handleBatchMenu}
              DropdownMenu={<SortDisableMenu SelectAll={this.state.BatchNumber}/>}
          />
          </TabPane>

        </Tabs>
      </div>
    )
  }
  componentDidMount(){
    this.handleChangeTabPane()
  }
}

export default FragmentClassify
