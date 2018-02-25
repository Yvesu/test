import React, { Component } from 'react'
import { Tabs, Badge, Button } from 'antd'
const TabPane = Tabs.TabPane

import TableContainer from '../../common/table/TableContainer'
import { sortNormal ,sortDisable } from '../common/table/columns/MaterialColumns'
import SortNormalMenu from '../common/table/dropdownMenu/SortNormalMenu'
import SortDisableMenu from '../common/table/dropdownMenu/SortDisableMenu'
import MaodalSort from '../modal/ModalSort'
//模板 - 分类

class TemplateSort extends Component{
  constructor(props){
    super(props)
    this.state={
      BatchNumber:[]
    }
  }
  handleFormData=()=>{
    let formData= new FormData()
    formData.append('active',0)
    return formData
  }
  handleBatchMenu=(value)=>{
    this.setState({
      BatchNumber:value
    })
  }
  render(){
    return(
      <div className="card-container card_box">
        <Tabs type="card"  tabBarExtraContent={<MaodalSort name='template/classify/cover/admins/'/> } >
          <TabPane tab={<p>正常
            <Badge count={15} overflowCount={999}
              style={{backgroundColor: '#fff', color: '#999',
               boxShadow: '0 0 0 1px #d9d9d9 inset',margin:'-3px 0 0 15px'}}
             /></p>} key="1" >

            <TableContainer columns={sortNormal} uri="/api/admins/fodder/fragment/type"
            // formData={this.handleFormData()}
              onChange={this.handleBatchMenu}
              DropdownMenu={<SortNormalMenu SelectAll={this.state.BatchNumber}/>}
            />
          </TabPane>
          <TabPane tab={<p>停用
            <Badge count={169}  overflowCount={999}
               style={{backgroundColor: '#fff', color: '#999',
               boxShadow: '0 0 0 1px #d9d9d9 inset',margin:'-3px 0 0 15px'}}
             /></p>} key="2" >

            <TableContainer columns={sortDisable} uri="/api/admins/fodder/fragment/type"
            // formData={this.handleFormData()}
              onChange={this.handleBatchMenu}
              DropdownMenu={<SortDisableMenu SelectAll={this.state.BatchNumber}/>}
            />
          </TabPane>

        </Tabs>
      </div>
    )
  }
}

export default TemplateSort
